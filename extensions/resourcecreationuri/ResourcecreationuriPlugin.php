<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';
require_once realpath(dirname(__FILE__)) . '/classes/ResourceUriGenerator.php';

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
    private $_deleteData = array();

    /**
     * @var Statements Array for statements to insert
     */
    private $_insertData = array();

    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $_deleteModel = null;

    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $_insertModel = null;

    /**
     * Try to generate nice uri if new resource uri is found
     *
     * @param   $event triggered Erfurt_Event
     *
     * @return  null
     */
    public function onUpdateServiceAction($event)
    {
        // set values from event
        $this->_insertModel = $event->insertModel;
        $this->_insertData  = $event->insertData;
        $this->_deleteModel = $event->deleteModel;
        $this->_deleteData  = $event->deleteData;

        $flag = false;

        // SPARQL/Update can be DELETE only
        // $_insertModel is null in this case
        if ($this->_insertModel instanceof Erfurt_Rdf_Model) {
            $subjectArray = array_keys($this->_insertData);
            $subjectUri   = current($subjectArray);
            $pattern      = '/^'
                // URI Component
                . addcslashes($this->_insertModel->getBaseUri() . $this->_privateConfig->newResourceUri, './')
                // MD5 Component
                . '\/([A-Z]|[0-9]){32,32}'
                . '/i';

            $gen = new ResourceUriGenerator($this->_insertModel, $this->_pluginRoot . 'plugin.ini');

            if (count($event->insertData) == 1 && preg_match($pattern, $subjectUri)) {
                $newUri = $gen->generateUri($subjectUri, ResourceUriGenerator::FORMAT_RDFPHP, $this->_insertData);
                $temp   = array();
                foreach ($this->_insertData[$subjectUri] as $p => $o) {
                    $temp[$newUri][$p] = $o;
                }
                $this->_insertData = $temp;
                $flag             = true;
            }
        }

        //writeback on event
        $event->insertModel = $this->_insertModel;
        $event->insertData  = $this->_insertData;
        $event->deleteModel = $this->_deleteModel;
        $event->deleteData  = $this->_deleteData;

        if ($flag) {
            $event->changes = array(
                'original' => $subjectUri,
                'changed'  => $newUri,
            );
        }
    }
}
