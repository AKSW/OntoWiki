<?php
class INI
{
    /**
     *  WRITE
     */
    static function write($filename, $ini) 
    {
        $string = '';
        foreach (array_keys($ini) as $key) {
            $string .= '['.$key."]\n";
            $string .= INI::write_get_string($ini[$key], '')."\n";
        }
        file_put_contents($filename, $string);
    }
    /**
     *  write get string
     */
    static function write_get_string(& $ini, $prefix) 
    {
        $string = '';
        ksort($ini);
        foreach ($ini as $key => $val) {
            if (is_array($val)) {
                $string .= INI::write_get_string($ini[$key], $prefix.$key.'.');
            } else {
                $string .= $prefix.$key.' = '.str_replace("\n", "\\\n", INI::set_value($val))."\n";
            }
        }
        return $string;
    }
    /**
     *  manage keys
     */
    static function set_value($val) 
    {
        if ($val === true) { 
            return 'true'; 
        } else if ($val === false) { 
            return 'false';
        }
        return $val;
    }
    /**
     *  READ
     */
    static function read($filename) 
    {
        $ini = array();
        $lines = file($filename);
        $section = 'default';
        $multi = '';
        foreach ($lines as $line) {
            if (substr($line, 0, 1) !== ';') {
                $line = str_replace("\r", "", str_replace("\n", "", $line));
                if (preg_match('/^\[(.*)\]/', $line, $m)) {
                    $section = $m[1];
                } else if ($multi === '' && preg_match('/^([a-z0-9_.\[\]-]+)\s*=\s*(.*)$/i', $line, $m)) {
                    $key = $m[1];
                    $val = $m[2];
                    if (strstr($val, ";")) {
                        $parts = explode(";", $val, 2);
                        $val = trim($parts[0]);
                    }
                    if (substr($val, -1) !== "\\") {
                        $val = trim($val);
                        INI::manage_keys($ini[$section], $key, $val);
                        $multi = '';
                    } else {
                        $multi = substr($val, 0, -1)."\n";
                    }
                } else if ($multi !== '') {
                    if (substr($line, -1) === "\\") {
                        $multi .= substr($line, 0, -1)."\n";
                    } else {
                        INI::manage_keys($ini[$section], $key, $multi.$line);
                        $multi = '';
                    }
                }
            }
        }
       
        //$buf = get_defined_constants(true);
        $consts = array();
        /*foreach($buf['user'] as $key => $val) {
            $consts['{'.$key.'}'] = $val;
        }*/
        array_walk_recursive($ini, array('INI', 'replace_consts'), $consts);
        return $ini;
    }
    /**
     *  manage keys
     */
    static function get_value($val) 
    {
        if (preg_match('/^-?[0-9]$/i', $val)) { 
            return intval($val);
        } else if (strtolower($val) === 'true') { 
            return true; 
        } else if (strtolower($val) === 'false') { 
            return false; 
        } else if (preg_match('/^"(.*)"$/i', $val, $m)) { 
            return $m[1]; 
        } else if (preg_match('/^\'(.*)\'$/i', $val, $m)) { 
            return $m[1];
        }
        return $val;
    }
    /**
     *  manage keys
     */
    static function get_key($val) 
    {
        if (preg_match('/^[0-9]$/i', $val)) { 
            return intval($val); 
        }
        return $val;
    }
    /**
     *  manage keys
     */
    static function manage_keys(& $ini, $key, $val) 
    {
        if (preg_match('/^([a-z0-9_-]+)\.(.*)$/i', $key, $m)) {
            INI::manage_keys($ini[$m[1]], $m[2], $val);
        } else if (preg_match('/^([a-z0-9_-]+)\[(.*)\]$/i', $key, $m)) {
            if ($m[2] !== '') {
                $ini[$m[1]][INI::get_key($m[2])] = INI::get_value($val);
            } else {
                $ini[$m[1]][] = INI::get_value($val);
            }
        } else {
            $ini[INI::get_key($key)] = INI::get_value($val);
        }
    }
    /**
     *  replace utility
     */
    static function replace_consts(& $item, $key, $consts) 
    {
        if (is_string($item)) {
            $item = strtr($item, $consts);
        }
    }
}

if ($argc != 2) {
    echo "usage extensions-ini2n3.sh <extension-name>"; exit(-1);
} else {
    $extension = $argv[1];
    echo <<<EOT
!!!!REMOVE THIS LINE AFTER YOU HAVE REVIEWED/FIXED THIS FILE!!!!
@prefix xsd: <http://www.w3.org/2001/XMLSchema#>.
@prefix doap: <http://usefulinc.com/ns/doap#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owconfig: <http://ns.ontowiki.net/SysOnt/ExtensionConfig/> .
@prefix extension: <http://ns.ontowiki.net/Extensions/> .
@prefix event: <http://ns.ontowiki.net/SysOnt/Events/> .
@prefix : <http://ns.ontowiki.net/Extensions/$extension> .

EOT;
    ob_start();
    $path = __DIR__."/../../extensions/".$extension."/default.ini";
    $config = INI::read($path);
    //var_dump($config);
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
        private $_bnCounter = 0;
        private $_parent = null;
        
        function resetLastSubject()
        {
            echo ' .'.PHP_EOL;
            $this->_lastSubject = null;
        }
        
        function addBN($subj, $prop)
        {
            
            $bn = $this->_bnCounter++;
            $bn = '_:'.$bn;
            //echo "start bn for ".$prop." bn=".$bn.PHP_EOL;
            $this->printStatement($subj, $prop, '[');
            $this->_parent = $this->_lastSubject;
            $this->_lastSubject = $bn;
            return $bn;
        }
        
        function endBN()
        {
            $this->flush();
            echo ']';
            $this->_lastSubject = $this->_parent;
        }
        
        function getObject($property, $value)
        {
            if (is_string($value)) {
                $value = addslashes($value);
            } 
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
            if (substr($this->_lastSubject, 0, 2) == '_:' && substr($s, 0, 2) != '_:') {
                //echo substr($s, 0, 2).PHP_EOL;
                //echo "end bn implicitly. old: ".$this->_lastSubject. " s: $s p: $p o:$o".PHP_EOL;
                $this->endBN();
            }
            if ($this->_lastSubject == null) {
                $this->_lastSubject = $s;
                echo $s.' '. $p .' '.$o;
            } else if ($this->_lastSubject == $s) {
                echo ';'.PHP_EOL.'  '. $p .' '.$o;
            } else {
                $this->_lastSubject = $s;
                echo '.'.PHP_EOL.$s.' '. $p .' '.$o;
            }
        }
        function __destruct() 
        {
           $this->flush();
        }

        function flush() 
        {
           echo '.'.PHP_EOL;
        }
    }
    class NestedPropertyAndModuleHandler
{
                            
        public $modules = array();
        public $properties = array();

        /**
         *
         * @var ExtensionSerializer 
         */
        private $_printer = null;

        private $_subject = null;
        
        private $_parent = null;

        function __construct(ExtensionSerializer $_printer, $_subj)
        {
            $this->_printer = $_printer;
            $this->_subject = $_subj;
        }
        
        function printProperty($name, $value, $i, $parent)
        {
            $this->_parent = $this->_subject;
            $bnUri = $this->_printer->addBN($this->_subject, 'owconfig:config');
            $this->_subject = $bnUri;
            $this->_printer->printStatement($bnUri, 'a', 'owconfig:Config;');
            $this->_printer->printStatement($bnUri, 'owconfig:id', '"'.$name.'";');
            $this->_printer->printStatement($bnUri, 'rdfs:comment', '"fixme";');
            
            if (is_array($value)) {
                foreach ($value as $key => $value) {
                    if (is_array($value)) { 
                        foreach ($value as $subKey => $subValue) {   
                            $this->printProperty($subKey, $subValue, $i +1, $key);
                        } 
                    } else {
                        $this->_printer->printStatement(
                            $bnUri, 
                            $this->_printer->getPredicate($key, ''),
                            $this->_printer->getObject($key, $value)
                        );
                    }
                }   
            } else {
                $this->_printer->printStatement(
                    $bnUri, 
                    $this->_printer->getPredicate($name, ''),
                    $this->_printer->getObject($name, $value)
                );
            }

            $this->_printer->endBN();
        }


        function printN3()
        {
            foreach ($this->modules as $name => $values) {
                $subject = ':'.ucfirst($name);
                $this->_printer->printStatement($subject, 'a', 'owconfig:Module');
                $this->_printer->printStatement($subject, 'rdfs:label', '"'.ucfirst($name).'"');
                foreach ($values as $prop => $val) {
                    $this->_printer->printStatement(
                        $subject, 
                        $this->_printer->getPredicate($prop, ''), 
                        $this->_printer->getObject($prop, $val)
                    );
                }
                $this->_printer->resetLastSubject();
            }
            
            if (is_array($this->properties)) {
                foreach ($this->properties as $name => $value) {
                    $this->printProperty($name, $value, 1, null);
                }
            } else var_dump($this->properties);
        }

    }
    $subject = ':'.$extension;
    $es = new ExtensionSerializer();
    $es->printStatement($subject, 'a', 'doap:Project');
    $mp = new NestedPropertyAndModuleHandler($es, $subject);
    
    foreach ($config as $sectionname => $sectionconf) {
        if ($sectionname == 'modules') {
            $mp->modules = $sectionconf;
            continue;
        } else if ($sectionname == 'private') {
            $mp->properties = $sectionconf;
            continue;
        }
        if (is_array($sectionconf)) {
            foreach ($sectionconf as $property => $value) {
                if ($sectionname == 'events') {
                    $predicate = 'owconfig:helperEvents';
                    $object = 'event:'.$value;
                    $es->printStatement($subject, $predicate, $object);
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
        } else {
            //parsing a property that has no section -> global section (the first lines until the first section)
            $property = $sectionname;
            $value = $sectionconf;
            
            $predicate = $es->getPredicate($property, $sectionname);
            $object = $es->getObject($property, $value);
            $es->printStatement($subject, $predicate, $object);
        }
    }
    
    $mp->printN3();
    
    $res = ob_get_clean();
    
    $res = str_replace(";;", ";", $res);
    $res = str_replace("[;", "[", $res);
    $res = preg_replace("/\\]\.\n_:[0-9]*/", "] ; \n", $res);
    echo $res;

}
