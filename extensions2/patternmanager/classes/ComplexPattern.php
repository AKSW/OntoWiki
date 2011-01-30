<?php

/**
 * Class for Complex Patterns consisiting of multiple basic patterns.
 * Subpatterns (parts) of a complex pattern are being executed in a
 * batch. This class provides ability to perform operations on all
 * available subpatterns at once.
 *
 * @copyright   Copyright (c) 2010 {@link http://aksw.org AKSW}
 * @license     http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package
 * @subpackage
 * @author      Christoph Rieß <c.riess.dev@googlemail.com>
 */

class ComplexPattern {

    /**
     * @var array Stores subpattern object in well-defined order
     */
    private $_subPattern = array();

    /**
     *
     * @var string. Label that identifies the pattern
     */
    private $_label = '';

    /**
     *
     * @var string. Textual descriptions that explains what this pattern does
     */
    private $_description = '';

    /**
     * @var Pattern Engine
     */
    private $_engine = null;
    
    /**
     * Storing annotation in this attribute (term-state, pattern level ...) 
     * @var array
     */
    private $_annotations = array();

    /**
     * Creates complex pattern initally
     */
    public function __construct() {

    }
    
    /**
     * Set annotation on pattern
     * @param $key string
     * @param $value mixed (string most likely)
     */
    public function setAnnotation($key, $value) {
        $this->_annotations[$key] = $value;
    }
    
    /**
     * Get Annotation value
     * @param $key string
     * @return mixed (string most likely)
     */
    public function getAnnotation($key) {
        if ( isset($this->_annotations[$key]) ) {
            return $this->_annotations[$key];
        } else {
            return null;
        }
    }

    /**
     * Parses from JSON to ComplexPattern object
     *
     * @param $json
     */
    public function fromArray($array, $asJson = false) {

        if ($asJson) {
	        $data = json_decode($array, true);
        } else {
	        $data = $array;
	    }

		$this->_label = $data['label'];
		$this->_description = $data['desc'];

        foreach ($data['subPattern'] as $subpattern) {

            $pattern = new BasicPattern();
			// set variables
			foreach($subpattern['V'] as $key => $variable) {

				$pattern->addVariable($variable['name'], $variable['type'], $variable['desc']);
			}
			// set select query
			$pattern->setSelectQuery($subpattern['S']);
			// set update queries
			foreach($subpattern['U'] as $key => $updateQuery) {

				$pattern->addUpdateQuery($updateQuery['pattern'], $updateQuery['type']);
			}

			$pattern->setLabel($subpattern['label']);
			$pattern->setDescription($subpattern['desc']);
            $this->appendElement($pattern);
        }
    }

    /**
     * Parses to JSON from ComplexPattern object
     *
     * @param $json
     */
    public function toArray($asJson = false) {

        $data = array();

        ksort($this->_subPattern, SORT_NUMERIC);

        $basicPattern = new BasicPattern();

        foreach ($this->_subPattern as $i => $basicPattern) {

            $data['subPattern'][$i] = $basicPattern->toArray();

        }

        $data['label'] = $this->getLabel();
        $data['desc'] = $this->getDescription();

        if ($asJson) {
            return Zend_Json::encode($data);
        } else {
            return $data;
        }

    }

    /**
     * Sets the label
     *
     * @param $label
     */
    public function setLabel($label) {
        $this->_label = (string) $label;
    }

    /**
     * Returns the label
     *
     * @return string $label
     */
    public function getLabel() {
        return (string) $this->_label;
    }

    /**
     * Sets the description
     *
     * @param $desc
     */
    public function setDescription($desc) {
        $this->_description = (string) $desc;
    }

    /**
     * Returns the description
     *
     * @return $desc
     */
    public function getDescription() {
        return (string) $this->_description;
    }

    /**
     * Sets the backend and execution engine
     *
     * @param $engine
     */
    public function setEngine($engine) {

        $this->_engine = $engine;

        foreach ($this->_subPattern as $pattern) {
            $pattern->setEngine($engine);
        }

    }

    /**
     *
     *
     * @return $engine
     */
    public function getEngine() {
        return $this->_engine;
    }

    public function getElement($index) {

    }

    /**
     * Returns array of subpatterns
     *
     * @return array of objects of BasicPattern
     */
    public function getElements() {
        return $this->_subPattern;
    }

    /**
     *
     *
     * @param int $index
     * @param string $element
     */
    public function setElement($index, $element) {
        $this->_subPattern[$index] = $element;
    }

    public function appendElement($element) {
        $this->_subPattern[] = $element;
    }

    public function getVariables($withBound = true, $noTemp = true) {

        $result = array();
        $intersection = array();

        foreach ($this->_subPattern as $pattern) {

            foreach ($pattern->getVariables($withBound, $noTemp) as $set) {
                if ( in_array($set,$result) ) {
                    // existing don't set again
                } else {
                    $result[$set['name']] = $set;
                }

            }

            if (isset($count)) {
                $count++;
                $intersection = array_intersect($intersection, $pattern->getVariables($withBound, $noTemp));
            } else {
                $intersection = $pattern->getVariables($withBound, $noTemp);
            }

            $count = 1;

        }

        return $result;

    }

    public function bindVariable($var, $value) {

        foreach ($this->_subPattern as $pattern) {

            try {
                $pattern->bindVariable($var,$value);
            } catch (RuntimeException $e) {
                // do nothing
            }

        }

    }

    public function executeSelect() {

        foreach ($this->_subPattern as $pattern) {
            $pattern->executeSelect();
        }

    }

    public function executeUpdate() {

        foreach ($this->_subPattern as $pattern) {
            $pattern->executeUpdate();
        }

    }

    public function execute() {

        foreach ($this->_subPattern as $pattern) {
            $pattern->executeSelect();
            $pattern->executeUpdate();
        }

    }

}
