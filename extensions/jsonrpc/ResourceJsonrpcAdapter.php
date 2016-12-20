<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2014-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * JSON RPC Class, this wrapper class is for all model RPC calls
 *
 * @category   OntoWiki
 * @package    Extensions_Jsonrpc
 * @copyright  Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ResourceJsonrpcAdapter
{
    private $_store  = null;
    private $_erfurt = null;
    private $_config = null;

    public function __construct()
    {
        $this->_store  = Erfurt_App::getInstance()->getStore();
        $this->_erfurt = Erfurt_App::getInstance();
        $this->_config = $this->_erfurt->getConfig();
    }

    /**
     * @desc get a resource as RDF/JSON
     *
     * @param string modelIri
     * @param string resourceIri
     * @param string format
     *
     * @return string
     */
    public function get($modelIri, $resourceIri, $format = 'rdfjson')
    {
        if (!$this->_store->isModelAvailable($modelIri)) {
            throw new Erfurt_Exception('Error: Model "' . $modelIri . '" is not available.');
        }
        $editable = $this->_store->getModel($modelIri)->isEditable();
        $supportedFormats = Erfurt_Syntax_RdfSerializer::getSupportedFormats();
        if (!isset($supportedFormats[$format])) {
            throw new Erfurt_Exception('Error: Format "' . $format . '" is not supported by serializer.');
        }
        $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);
        // create hash for current status of resource
        $currentDataHash = $this->_getCurrentResourceHash($modelIri, $resourceIri);
        $return = new stdClass();
        $return->dataHash = $currentDataHash;
        $return->editable = $editable;
        $return->data = $serializer->serializeResourceToString($resourceIri, $modelIri);
        return $return;
    }

    /**
     * @desc update a modified resource
     *
     * @param string modelIri
     * @param string resourceIri
     * @param string data
     * @param string format
     * @param string origDataHash
     *
     * @return string
     */
    public function update($modelIri, $resourceIri, $data, $origDataHash, $format = 'rdfjson')
    {
        $model = $this->_store->getModel($modelIri);
        if (!$model->isEditable()) {
            throw new Erfurt_Exception('Error: Model "' . $modelIri . '" is not available.');
        }
        // TODO check for formats supported by the parser (not yet implemented)

        // calculate hash of current status and compare to commited hash
        $currentDataHash = $this->_getCurrentResourceHash($modelIri, $resourceIri);

        if ($currentDataHash !== $origDataHash) {
            throw new Erfurt_Exception('Error: Resource "' . $resourceIri . '" was edited during your session.');
        }

        // get current statements of resource
        $resource = $model->getResource($resourceIri);
        $originalStatements = $resource->getDescription();

        // get new statements of resource
        $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat($format);
        $modifiedStatements = $parser->parse($data, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);

        $model->updateWithMutualDifference($originalStatements, $modifiedStatements);

        return true;
    }

    /**
     * @desc counts the number of statements of a model
     *
     * @param string modelIri
     * @param string whereSpec
     * @param string countSpec
     *
     * @return string
     */
    public function count($modelIri, $whereSpec = '{?s ?p ?o}', $countSpec = '*')
    {
        return $this->_store->countWhereMatches($modelIri, $whereSpec, $countSpec);
    }

    /**
     * @desc create a new knowledge base
     *
     * @param string $modelIri
     *
     * @return bool
     */
    public function create($modelIri)
    {
        $this->_store->getNewModel($modelIri);

        return true;
    }

    /**
     * @desc drop an existing knowledge base
     *
     * @param string $modelIri
     *
     * @return bool
     */
    public function drop($modelIri)
    {
        $this->_store->deleteModel($modelIri);

        return true;
    }

    /**
     * This methid calculates a hash value for a resource including its IRI and all its properties.
     *
     * @param string $modelIri the IRI of the model
     * @param string $resourceIri the IRI of the resource
     *
     * @return string with the hash value
     */
    private function _getCurrentResourceHash($modelIri, $resourceIri)
    {
        $resource = $this->_store->getModel($modelIri)->getResource($resourceIri);
        $statements = $resource->getDescription();
        return md5(serialize($statements));
    }
}
