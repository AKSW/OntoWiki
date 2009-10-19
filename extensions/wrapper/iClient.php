<?php
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 */
interface iClient
{
  public function get($uri, $raw_data = null);
  public function request($method = null);
  
  public function getArtist($uri);
  public function getRelease($uri);
  public function getLabel($uri);
  public function getTrack($uri);

  public function parseArtist($uri, $raw_data);
  public function parseRelease($uri, $raw_data);
  public function parseLabel($uri, $raw_data);
  public function parseTrack($uri, $raw_data);
}

?>