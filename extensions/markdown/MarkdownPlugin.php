<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @category   OntoWiki
 * @package    Extensions_Markdown
 */
class MarkdownPlugin extends OntoWiki_Plugin
{

    public function init()
    {
        $this->properties = array_values($this->_privateConfig->properties->toArray());
        if (isset($this->_privateConfig->datatypes) && $this->_privateConfig->datatypes instanceof Zend_Config) {
            $this->datatypes = array_values($this->_privateConfig->datatypes->toArray());
        } else {
            if (isset($this->_privateConfig->datatypes)) {
                $this->datatypes = array($this->_privateConfig->datatypes);
            }
        }
    }

    public function onDisplayLiteralPropertyValue($event)
    {
        if (
            in_array($event->property, $this->properties)
            || in_array($event->datatype, $this->datatypes)
        ) {
            require_once 'parser/markdown.php';

            $mdString = $this->_parseMetadata($event->value);

            return Markdown($mdString);
        }
    }

    private function _parseMetadata($mdString)
    {
        $metaData = array();

        $lastKey = null;
        $lineBuffer = '';
        $strlen = strlen($mdString);
        for ($i = 0; $i < $strlen; $i++) {
            $curChar = substr($mdString, $i, 1);
            if ($curChar != "\n") {
                $lineBuffer .= $curChar;
            } else {
                if (empty(trim($lineBuffer))) {
                    // end of metadata
                    break;
                } else if (substr($lineBuffer, 0, 1) == ' ' && $lastKey !== null) {
                    // merge to previous line
                    $metaData[$lastKey] .= PHP_EOL . trim($lineBuffer);
                    if (substr($lineBuffer, -2) != '  ') {
                        $lastKey = null;
                    }
                    $lineBuffer = '';
                } else {
                    $colon = strpos($lineBuffer, ':');
                    if ($colon !== false) {
                        $key = trim(substr($lineBuffer, 0, $colon));
                        $value = trim(substr($lineBuffer, $colon + 1));
                        $metaData[$key] = trim($value);
                        if (substr($lineBuffer, -2) == '  ') {
                            $lastKey = $key;
                        } else {
                            $lastKey = null;
                        }
                        $lineBuffer = '';
                    } else {
                        // This is no metadata
                        break;
                    }
                }
            }
        }

        if (count($metaData) == 0) {
            return $mdString;
        } else {
            return substr($mdString, $i + 1);
        }
    }
}
