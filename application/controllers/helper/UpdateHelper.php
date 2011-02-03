<?php

class UpdateHelper
{
    public static function findStatementsForObjectsWithHashes($model, $indexWithHashedObjects, $hashFunc = 'md5')
    {
        $queryOptions = array(
            'result_format' => 'extended'
        );
        $results = array();
        foreach ($indexWithHashedObjects as $subject => $predicates) {
            foreach ($predicates as $predicate => $hashedObjects) {
                $query = "SELECT ?o FROM <(string)$model> WHERE {<$subject> <$predicate> ?o .}";
                if ($result = $model->sparqlQuery($query, $queryOptions)) {
                    $bindings = $result['results']['bindings'];
                    
                    for ($i = 0, $max = count($bindings); $i < $max; $i++) {
                        $currentObject = $bindings[$i]['o'];
                        $objectString = self::buildLiteralString(
                            $currentObject['value'], 
                            isset($currentObject['datatype']) ? $currentObject['datatype'] : null, 
                            isset($currentObject['lang']) ? $currentObject['lang'] : null);
                        
                        $count = count($hashedObjects);
                        for ($j = 0; $j < $count; $j++) {
                            if ($hashFunc($objectString) === $hashedObjects[$j]) {
                                $objectSpec = array(
                                    'value' => $currentObject['value'], 
                                    'type'  => str_replace('typed-', '', $currentObject['type'])
                                );
                                if (isset($currentObject['datatype'])) {
                                    $objectSpec['datatype'] = $currentObject['datatype'];
                                } else if (isset($currentObject['lang'])) {
                                    $objectSpec['lang'] = $currentObject['lang'];
                                }
                                
                                // add current statement to result
                                if (!isset($results[$subject])) {
                                    $results[$subject] = array();
                                }
                                if (!isset($results[$subject][$predicate])) {
                                    $results[$subject][$predicate] = array();
                                }

                                $results[$subject][$predicate][] = $objectSpec;
                                
                                continue 2;
                            }
                        }
                    }
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Builds a SPARQL-compatible literal string with long literals if necessary.
     *
     * @param string $value
     * @param string|null $datatype
     * @param string|null $lang
     * @return string
     */
    public static function buildLiteralString($value, $datatype = null, $lang = null)
    {
        $longLiteral = false;
        $quoteChar   = (strpos($value, '"') !== false) ? "'" : '"';
        $value       = (string)$value;
        
        // datatype-specific treatment
        switch ($datatype) {
            case 'http://www.w3.org/2001/XMLSchema#boolean':
                $search  = array('0', '1');
                $replace = array('false', 'true');
                $value   = str_replace($search, $replace, $value);
                break;
            case '':
            case null:
            case 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral':
            case 'http://www.w3.org/2001/XMLSchema#string':
                $value = addcslashes($value, $quoteChar);
                
                /** 
                 * Check for characters not allowed in a short literal
                 * {@link http://www.w3.org/TR/rdf-sparql-query/#rECHAR}
                 */
                if ($pos = preg_match('/[\x5c\r\n"]/', $value)) {
                    $longLiteral = true;
                    // $value = trim($value, "\n\r");
                    // $value = str_replace("\x0A", '\n', $value);
                }
                break;
        }
        
        // add short, long literal quotes respectively
        $value = $quoteChar . ($longLiteral ? ($quoteChar . $quoteChar) : '')
               . $value 
               . $quoteChar . ($longLiteral ? ($quoteChar . $quoteChar) : '');
        
        // add datatype URI/lang tag
        if (!empty($datatype)) {
            $value .= '^^<' . (string)$datatype . '>';
        } else if (!empty($lang)) {
            $value .= '@' . (string)$lang;
        }
        
        return $value;
    }
}