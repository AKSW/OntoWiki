<?php
/**
* This file is part of the {@link http://ontowiki.net OntoWiki} project.
*
* @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
*/

require_once 'OntoWiki/Plugin.php';
require_once OntoWiki::getInstance()->extensionManager->getExtensionPath().'resourcecreationuri/classes/ResourceUriGenerator.php';

/**
 * Plugin that tries to make nice uris if new resources are created.
 *
 * @category   OntoWiki
 * @package    Extensions_Resourcecreationuri
 */
class ResourcecreationuriPlugin extends OntoWiki_Plugin
{
    
    /**
     * @var Statements Array for statements to delete
     */
    private $deleteData     = array();
    
    /**
     * @var Statements Array for statements to insert
     */
    private $insertData     = array();
    
    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $deleteModel    = null;
    
    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $insertModel    = null;
    
    /**
     * Try to generate nice uri if new resource uri is found
     * @param   $event triggered Erfurt_Event
     * @return  null
     */
    public function onUpdateServiceAction($event)
    {
        // set values from event
        $this->insertModel  = $event->insertModel;
        $this->insertData   = $event->insertData;
        $this->deleteModel  = $event->deleteModel;
        $this->deleteData   = $event->deleteData;
        
        $flag = false;
        
        // SPARQL/Update can be DELETE only
        // $insertModel is null in this case
        if ($this->insertModel instanceof Erfurt_Rdf_Model) {
            $subjectArray   = array_keys($this->insertData);
            $subjectUri     = current($subjectArray);
            $pattern        = '/^'
                            // URI Component
                            . addcslashes($this->insertModel->getBaseUri() . $this->_privateConfig->newResourceUri,'./')
                            // MD5 Component
                            . '\/([A-Z]|[0-9]){32,32}'
                            . '/i';

            // $nameParts = $this->loadNamingSchema();
            
            $gen = new ResourceUriGenerator($this->insertModel,$this->_pluginRoot . 'plugin.ini');

            if ( count($event->insertData) == 1 && preg_match($pattern,$subjectUri) ) {
                $newUri = $gen->generateUri($subjectUri, ResourceUriGenerator::FORMAT_RDFPHP, $this->insertData);
                $temp   = array();
                foreach ($this->insertData[$subjectUri] as $p => $o) {
                    $temp[$newUri][$p] = $o;
                }
                $this->insertData = $temp;
                $flag = true;
            } else {
                //do nothing
            }
        }
        
        //writeback on event
        $event->insertModel = $this->insertModel;
        $event->insertData  = $this->insertData;
        $event->deleteModel = $this->deleteModel;
        $event->deleteData  = $this->deleteData;
        
        if ($flag) {
            $event->changes = array(
                'original' => $subjectUri, 
                'changed'  => $newUri, 
            );
        }
    }
}
