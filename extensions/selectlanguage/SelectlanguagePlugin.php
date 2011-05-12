<?php
require_once 'OntoWiki/Plugin.php';

class SelectlanguagePlugin extends OntoWiki_Plugin
{
    protected $_config = null;
    protected $_supportedLanguages = null;
    public $owApp;

    public function init() {
        $this->_config = $this->_privateConfig;
        $this->_supportedLanguages = $this->_config->languages->toArray();
        $this->owApp = OntoWiki::getInstance();
    }

    public function onBeforeInitController() {
        // Translation hack in order to enable the plugin to translate...
        $translate = $this->owApp->translate;
        $translate->addTranslation(
            $this->_pluginRoot . 'languages',
            null,
            array('scan' => Zend_Translate::LOCALE_FILENAME)
        );
        $locale = $this->owApp->getConfig()->languages->locale;
        $translate->setLocale($locale);
 
        $appMenu    = OntoWiki_Menu_Registry::getInstance()->getMenu('application');
        $extrasMenu = $appMenu->getSubMenu('Extras');
        $lanMenuEntry      = $translate->_('Select Language', $this->owApp->config->languages->locale);
        $lanMenue = new OntoWiki_Menu();

        $request = new OntoWiki_Request();
        $getRequest =  $request->getRequestUri();
        foreach ($this->_supportedLanguages as $key => $value) {
            $getRequest = str_replace("&lang=".$key, "", $getRequest);
            $getRequest = str_replace("?lang=".$key, "", $getRequest);
        }
        foreach ($this->_supportedLanguages as $key => $value) {
            $url = $getRequest . ((strpos($getRequest, "?")) ? "&" : "?" ) . "lang=".$key;
            $lanMenue->appendEntry(
                            $translate->_($value, $this->owApp->config->languages->locale),
                            $url);
        }
        $extrasMenu->setEntry($lanMenuEntry, $lanMenue);
    }

    public function onPostBootstrap($event) {

        $request = new OntoWiki_Request();
        $requestedLanguage = $request->getParam("lang");
        $selectedLanguage = "";

        if (!empty($requestedLanguage)) {
            $selectedLanguage = $requestedLanguage;
            $_SESSION['selectedLanguage'] = $requestedLanguage;
        } else if (!empty($_SESSION['selectedLanguage'])) {
            $selectedLanguage = $_SESSION['selectedLanguage'];
        }

        //writing the selected Language back into configuration
        if (!empty($selectedLanguage)) {

            //Set Selected Language in the internal config object
            $this->owApp->config->languages->locale = $selectedLanguage;
            //Set the Selected Language in the language Variable of the OntoWiki Object
            $this->owApp->language = $selectedLanguage;
        }
    }
}
