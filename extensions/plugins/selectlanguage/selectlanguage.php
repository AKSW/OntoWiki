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

    
    public function createMenu() {
        // Translation hack in order to enable the plugin to translate...
        $translate = $this->owApp->translate;
        $translate->addTranslation(
            $this->_pluginRoot . 'languages',
            null,
            array('scan' => Zend_Translate::LOCALE_FILENAME)
        );
        $locale = $this->owApp->getConfig()->languages->locale;
        $translate->setLocale($locale);



        //Adding MenuEntries on the basis of the private plugin configuration
        $appMenu    = OntoWiki_Menu_Registry::getInstance()->getMenu('application');
        #var_dump($appMenu);die;
        $extrasMenu = $appMenu->getSubMenu('Extras');
        $lanMenuEntry      = $translate->_('select language', $this->owApp->config->languages->locale);
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

    //TODO: language is not available over request borders
    public function onBeforeInitController($event) {

        $request = new OntoWiki_Request();
        $config = $this->owApp->getConfig();

        $selLang = "";
        $reqLang = $request->getParam("lang");
        if (!empty($reqLang)) {
            $selLang =  $reqLang;
        } else if (!empty($_SESSION['selectedLanguage'])) {
            $selLang = $_SESSION['selectedLanguage'];
        }

        if (!empty( $reqLang)) {
            $_SESSION['selectedLanguage'] =  $reqLang;
        }
      
        if (!empty( $selLang)) {
            $config->languages->locale = $selLang;
        }

        //Menu is created at last, because link of languages should created after languageselection
        $this->createMenu();
    }



}