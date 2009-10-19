<?php
require_once 'Erfurt/Wrapper.php';

/**
 * Initial version of a wrapper for Twitter.
 * Currently this is only a demo. It shows how a wrapper can handle data
 * itself, as well as quering the store and removing data.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: twitter.php 3026 2009-05-05 07:19:58Z pfrischmuth $
 */
class ExifWrapper extends Erfurt_Wrapper
{
    protected $_cachedData = array();
    
    protected $_pattern = null;
    
    protected $_exifArray = null;
    
    public function getDescription()
    {
        return 'A simple wrapper to extract Exif data from images.';
    }
    
    public function getName()
    {
        return 'Exif';
    }
    
    public function init($config)
    {
        parent::init($config);
        
        $this->_pattern = "/^.*\.(jpg|tiff)$/";
    }
    
    public function isAvailable($uri, $graphUri)
    {
        $tmpDir = Erfurt_App::getInstance()->getTmpDir();
        
        require_once 'Zend/Http/Client.php';
        $client = new Zend_Http_Client($uri, array(
            'maxredirects'  => 0,
            'timeout'       => 30
        ));
        
        $response = $client->request();

        if ($response->getStatus() === 200) {
            $result = $response->getBody();   
        } else {
            $result = '';
        }
        
        $fileName = $tmpDir.'/'.md5($uri).'.jpg';
        $fhandle = fopen($fileName, 'w');
        fwrite($fhandle, $result);
        fclose($fhandle);
        
        $this->_exifArray = exif_read_data($fileName, '', false);
        unlink($fileName);
        
        if (count($this->_exifArray) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function isHandled($uri, $graphUri)
    {
        if (!extension_loaded('exif')) {
            return false;
        }
        
        if (preg_match($this->_pattern, $uri)) {
            return true;
        } 
        
        return false;
    }
    
    public function run($uri, $graphUri)
    {
        if (null === $this->_exifArray) {
            if (!$this->isAvailable($uri, $graphUri)) {
                return false;
            }
        }
        
        $exif = $this->_exifArray;
        
        $data = array();
        $data[$uri] = array();
        $data[$uri][EF_RDF_TYPE] = array();
        $data[$uri][EF_RDF_TYPE][] = array(
            'type'  => 'uri',
            'value' => $this->_config->properties->type
        );
        
        foreach ($this->_config->properties->exif->toArray() as $key => $value) {
            if (isset($exif[$key])) {
                $data[$uri][$value] = array();
                $data[$uri][$value][] = array(
                    'type'  => 'literal',
                    'value' => $exif[$key]
                );
            }
        }
        
        $fullResult['status_description'] = "Exif data found for URI $uri";
        $fullResult['add'] = $data;
        $fullResult['status_codes'] = array(Erfurt_Wrapper::NO_MODIFICATIONS);
        $fullResult['status_codes'][] = Erfurt_Wrapper::RESULT_HAS_ADD;
        
        return $fullResult;
    }
}
