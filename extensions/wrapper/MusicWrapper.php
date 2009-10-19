<?php

require_once 'Erfurt/Wrapper.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 */
abstract class MusicWrapper extends Erfurt_Wrapper
{
  protected $_cachedData = null;
  
  protected $_pattern = null;
  
  protected $_wrapperClient = null;
  
  // why should we query the store here as in twitter wrapper?
  // how should it get there when the pattern does not match?
  public function isHandled($uri, $graphUri)
  {
    return preg_match($this->_pattern, $uri);
  }
  
  protected function _setLocalCache($uri, $graphUri, $data)
  {
    if (!isset($this->_cachedData[$graphUri])) {
      $this->_cachedData[$graphUri] = array($uri => $data);
    } else {
      $this->_cachedData[$graphUri][$uri] = $data;
    }
  }
  
}

?>