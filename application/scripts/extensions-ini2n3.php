<?php
if ($argc != 2) {
    echo "usage extensions-ini2n3.sh <extension-name>"; exit(-1);
} else {
    $extension = $argv[1];
    echo <<<EOT
@prefix xsd: <http://www.w3.org/2001/XMLSchema#>.
@prefix doap: <http://usefulinc.com/ns/doap#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owconfig: <http://ns.ontowiki.net/SysOnt/ExtensionConfig/> .
@prefix extension: <http://ns.ontowiki.net/Extensions/> .
@prefix event: <http://ns.ontowiki.net/SysOnt/Events/> .
@prefix : <http://ns.ontowiki.net/Extensions/$extension> .

EOT;

    $path = __DIR__."/../../extensions/".$extension."/default.ini";
    $config = parse_ini_file($path, true);
    class ExtensionSerializer
{
          private $_map = array(
            'name'          => array(
                                'type'=>'literal',
                                'property'=>'doap:name'
                            ),
            'enabled'       =>array(
                                'type'=>'literal',
                                'property'=>'owconfig:enabled'
                            ),
            'author'        =>array(
                                'type'=>'literal',
                                'property'=>'doap:maintainer'
                            ),
            'templates'     =>array(
                                'type'=>'literal',
                                'property'=>'owconfig:templates'
                            ),
            'languages'     =>array(
                                'type'=>'literal',
                                'property'=>'owconfig:languages'
                            ),
            'helpers'        =>array(
                                'type'=>'literal',
                                'property'=>'owconfig:helpers'
                            ),
            'description'   =>array(
                                'type'=>'literal',
                                'property'=>'doap:description'
                            ),
            'authorUrl'     =>array(
                                'type'=>'uri',
                                'property'=>'doap:maintainer',

                            )
        );
        private $_lastSubject = null;
        function getObject($property, $value)
        {
            $value = addslashes($value);
            if (isset($this->_map[$property]) && $this->_map[$property]['type'] == 'uri') {
                $object =  '<'.$value.'>';
            } else if ($value == 'true' || $value == 'false') {
                 $object =  '"'.$value.'"^^xsd:boolean';
            } else if (is_bool($value)) {
                 $object =  '"'.($value ? 'true' : 'false').'"^^xsd:boolean';
            } else {
                 $object =  '"'.$value.'"';
            }
            return $object;
        }
        function getPredicate($property, $sectionname)
        {
            return ( 
                (!isset($this->_map[$property]) || $sectionname == 'private') ? 
                ':'.$property :
                $this->_map[$property]['property']
            );
        }

        function printStatement($s, $p, $o)
        {
            if ($this->_lastSubject == $s) {
                echo ';'.PHP_EOL.'  '. $p .' '.$o;
            } else {
                $this->_lastSubject = $s;
                echo $s.' '. $p .' '.$o;
            }
        }
        function __destruct() 
        {
           $this->flush();
        }
        function endSubject()
        {
           echo ';'.PHP_EOL; 
        }
        function flush() 
        {
           echo '.'.PHP_EOL;
        }
    }
    class NestedPropertyAndModuleHandler
{
                            
        private $_modules = array();
        private $_properties = array();

        /**
         *
         * @var ExtensionSerializer 
         */
        private $_printer = null;

        private $_subject = null;

        function __construct(ExtensionSerializer $_printer, $_subj)
        {
            $this->_printer = $_printer;
            $this->_subject = $_subj;
        }

        function addModuleProperty($module, $property, $value)
        {
            if (!isset($this->_modules[$module])) {
                $this->_modules[$module] = array();
            }   
            $this->_modules[$module][$property] = $value;
        }

        function addNestedPropertyValue($property, $value)
        {
            $parts = explode('.', $property);
            $reverseParts = array_reverse($parts);
            $curr =  $value;
            foreach ($reverseParts as $part) {
                $curr = array($part => $curr);
            }

            $this->_properties = array_merge_recursive($this->_properties, $curr);
        }
        
        function printProperty($name, $value, $i, $parent)
        {
            if (is_array($value)) {
                foreach ($value as $k => $v) {   
                    if (is_array($v)) {
                        echo PHP_EOL.str_repeat('    ', $i).'owconfig:config [';
                        echo PHP_EOL.str_repeat('    ', $i+1).'a owconfig:Config;';
                        echo PHP_EOL.str_repeat('    ', $i+1).'owconfig:id "'.$parent.'";';
                        echo PHP_EOL.str_repeat('    ', $i+1).'rdfs:comment "fixme";';
                    }
                    if (is_array($v)) {
                        $this->printProperty($k, $v, $i +1, $name);
                    } else {
                        $this->printProperty($k, $v, $i, $name);
                    }
                    if (is_array($v)) {
                        echo PHP_EOL.str_repeat('    ', $i).'];'.PHP_EOL;
                    }
                }
            } else {
                //echo PHP_EOL.str_repeat('    ',$i).'a owconfig:Config;';
                //echo PHP_EOL.str_repeat('    ',$i).'owconfig:id "'.$parent.'";';
                //echo PHP_EOL.str_repeat('    ',$i).'rdfs:comment "fixme";';
                echo PHP_EOL.str_repeat('    ', $i).$this->_printer->getPredicate($name, '').' '.
                        $this->_printer->getObject($name, $value).' ; '.PHP_EOL;
            }
        }


        function printN3()
        {
            $this->_printer->endSubject();
            foreach ($this->_modules as $name => $values) {
                $subject = ':'.ucfirst($name);
                foreach ($values as $prop => $val) {
                    $this->_printer->printStatement(
                        $subject, 
                        $this->_printer->getPredicate($prop, ''), 
                        $this->_printer->getObject($prop, $val)
                    );
                }
            }
            foreach ($this->_properties as $name => $value) {
                $this->printProperty($name, $value, 1, null);
            }
        }

    }
    $subject = ':'.$extension;
    $es = new ExtensionSerializer();
    $es->printStatement($subject, 'a', 'doap:Project');
    $mp = new NestedPropertyAndModuleHandler($es, $subject);
    
    foreach ($config as $sectionname => $sectionconf) {
        
        if (is_array($sectionconf)) {
            foreach ($sectionconf as $property => $value) {
                if ($sectionname == 'events') {
                    $predicate = 'owconfig:helperEvents';
                    $object = 'event:'.$value;
                    $es->printStatement($subject, $predicate, $object);
                } else if ($sectionname == 'modules') {
                    //todo
                } else {
                    if (strstr($property, '.') != false) {
                       $mp->addNestedPropertyValue($property, $value);
                    } else {
                        //this is not in global section and not in events
                        $predicate = $es->getPredicate($property, $sectionname);
                        if (is_array($value)) {
                            //should never happen
                        } else {
                            $object = $es->getObject($property, $value);
                            $es->printStatement($subject, $predicate, $object);
                        }
                    }
                }
            }
        } else {
            //parsing a property that has no section -> global section (the first lines until the first section)
            $property = $sectionname;
            if (strstr($property, '.') != false) {
                //split
                $parts = explode('.', $property, 3);
                $first = $parts[0];
                $name = $parts[1];
                $prop = $parts[2];
                if ($first == 'modules') {
                    $mp->addModuleProperty($name, $prop, $value);
                }
            }
            
            $value = $sectionconf;
            $predicate = $es->getPredicate($property, $sectionname);
            $object = $es->getObject($property, $value);
            $es->printStatement($subject, $predicate, $object);
        }
    }
    
    $mp->printN3();
}
