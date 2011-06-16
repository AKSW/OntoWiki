<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once('ImporterInterface.php');
/**
 * Abstract Class of CSV Importer
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage Csvimport
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
abstract class Importer implements ImporterInterface
{
    protected $configuration;
    protected $storedConfigurations;

    protected $uploadedFile;
    protected $parsedFile;
    protected $viewElements;
    protected $componentConfig;
    protected $view;
    protected $parserInstructions = array();

    public function __construct(&$view, $componentConfig, $parserInstructions = array()) {
        $this->view = $view;
        $this->componentConfig = $componentConfig;
        $this->parserInstructions = $parserInstructions;
        $this->initLog();
    }

    public function setConfiguration($configuration) {
        $this->configuration = $configuration;
    }

    public function setStoredConfigurations($configurations) {
        $this->storedConfigurations = $configurations;
    }

    public function setFile($uploadedFile) {

        $this->uploadedFile = $uploadedFile;
        if (is_readable($this->uploadedFile)) {
            $this->parseFile();
        }
    }

    public function getParsedFile() {
        return $this->parsedFile;
    }

    public function setParsedFile($parsedFile) {
        $this->parsedFile = $parsedFile;
    }

    public function getViewElements() {
        if (!empty($this->viewElements))
            return $this->viewElements;
        return array();
    }

    public static function initLog(){
        $path = 'extensions/components/csvimport/';
        if(!is_writable($path)) return -1;
        if(!is_dir($path.'logs/')) {
            if(!mkdir($path.'logs/', 0777)){
                echo "something was wrong while creating log at : " . $path;
                return 0;
            }
        }
        $fp = fopen($path.'logs/importer.log', 'w+');
        fwrite($fp, "");
        fclose($fp);
    }

    public static function logEvent($event){
        $path = 'extensions/components/csvimport/';
        if(!is_writable($path)) return -1;
        if(!is_dir($path.'logs/')) {
            if(!mkdir($path.'logs/', 0777)){
                echo "something was wrong while creating log at : " . $path;
                return 0;
            }
        }
        $fp = fopen($path.'logs/importer.log', 'a+');
        fwrite($fp, $event."\n");
        fclose($fp);
    }
}
