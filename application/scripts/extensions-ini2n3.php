<?php
class ExtensionSerializer
{
      private $_map = array(
        'name'          => array(
                            'type'=>'literal',
                            'property'=>'rdfs:label'
                        ),
        'enabled'       =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:enabled',
                            'datatype' => 'boolean'
                        ),
        'author'        =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:authorLabel'
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
        'caching'   =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:caching',
                            'datatype' => 'boolean'
                        ),
        'priority'   =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:priority'
                        ),
        'description'   =>array(
                            'type'=>'literal',
                            'property'=>'doap:description'
                        ),
        'contexts'   =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:context'
                        ),
        'title'   =>array(
                            'type'=>'literal',
                            'property'=>'rdfs:label'
                        ),
        'classes'   =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:class'
                        ),
        'action'   =>array(
                            'type'=>'literal',
                            'property'=>'owconfig:defaultAction'
                        ), 
        'authorUrl'     =>array(
                            'type'=>'uri',
                            'property'=>'doap:maintainer',

                        )
    );
    private $_lastSubject = null;
    private $_bnCounter = 0;
    private $_parent = null;
    private $_depth = 0;

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
        $this->_depth++;
        return $bn;
    }

    function endBN($uri = null)
    {
        /*if($uri != null){
            if($uri != $this->_lastSubject){
                return; //do not end this, this is not what you wanted to end
            }
        }*/
        //$this->flush();
        $this->_depth--;
        $i = str_repeat('    ', $this->_depth);
        echo PHP_EOL.$i.']';
        $this->_lastSubject = $this->_parent;
    }

    function getObject($property, $value)
    {
        if (is_string($value)) {
            $value = addslashes($value);
        }
        if ((isset($this->_map[$property]) && $this->_map[$property]['type'] == 'uri') || Erfurt_Uri::check($value)) {
             $object =  '<'.$value.'>';
        } else if (is_bool($value)) {
             $object =  '"'.($value ? 'true' : 'false').'"^^xsd:boolean';
        } else if (is_string($value) && $value == 'true' || $value == 'false') {
             $object =  '"'.($value == 'true' ? 'true' : 'false').'"^^xsd:boolean'; //why?
        } else if (isset($this->_map[$property]['datatype']) && $this->_map[$property]['datatype'] == 'boolean') {
             $object =  '"'.((bool)$value ? 'true' : 'false').'"^^xsd:boolean';
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
        //indent
        $i = str_repeat('    ', $this->_depth);
        /*if (substr($this->_lastSubject, 0, 2) == '_:' && substr($s, 0, 2) != '_:') {
            //echo substr($s, 0, 2).PHP_EOL;
            //echo "end bn implicitly. old: ".$this->_lastSubject. " s: $s p: $p o:$o".PHP_EOL;
            $this->endBN($this->_lastSubject);
        }*/
        if ($this->_lastSubject == null) {
            $this->_lastSubject = $s;
            echo $i.$s.' '. $p .' '.$o;
        } else if ($this->_lastSubject == $s) {
            echo ' ;'.PHP_EOL.$i.'  '. $p .' '.$o; 
        } else {
            $this->_lastSubject = $s;
            echo ' .'.PHP_EOL.$i.$s.' '. $p .' '.$o; 
        }
    }
    function __destruct() 
    {
       $this->flush();
    }

    function flush() 
    {
       echo ' .'.PHP_EOL;
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

    function printProperty($name, $value)
    {
        $this->_parent = $this->_subject;
        $bnUri = $this->_printer->addBN($this->_subject, 'owconfig:config');
        $this->_subject = $bnUri;
        $this->_printer->printStatement($bnUri, 'a', 'owconfig:Config;');
        $this->_printer->printStatement($bnUri, 'owconfig:id', '"'.$name.'";');
        //$this->_printer->printStatement($bnUri, 'rdfs:comment', '"fixme";');
        
        foreach ($value as $subKey => $subValue) {  
            if (!is_array($subValue)) {
                $this->_printer->printStatement(
                    $bnUri, 
                    $this->_printer->getPredicate($subKey, ''), 
                    $this->_printer->getObject($subKey, $subValue)
                );
            } else {
                if (!self::is_numeric($subValue)) {
                    $this->printProperty($subKey, $subValue);
                } else {
                    foreach ($subValue as $subSubKey => $subSubValue) {
                        if (!is_array($subSubValue)) {
                            $this->_printer->printStatement(
                                $bnUri, 
                                $this->_printer->getPredicate($subKey, ''), //omit the $subSubKey here!
                                $this->_printer->getObject($subKey, $subSubValue)
                            );
                        } else {
                            $this->printProperty($subKey, $subValue);
                        }
                    }
                }
            }
        } 
        
        $this->_printer->endBN($bnUri);
    }
    
    static private function is_assoc ($arr) 
    {
        return (is_array($arr) && count(array_filter(array_keys($arr), 'is_string')) == count($arr));
    }
    
    static private function is_numeric ($arr) 
    {
        return (is_array($arr) && count(array_filter(array_keys($arr), 'is_int')) == count($arr));
    }

    function printN3()
    {
        foreach ($this->modules as $name => $config) {
            //print a module
            $moduleUri = ':'.ucfirst($name);
            $this->_printer->printStatement($this->_subject, 'owconfig:hasModule', $moduleUri);
            $this->_printer->printStatement($moduleUri, 'a', 'owconfig:Module');
            if (!isset($config['title'])) {
                $this->_printer->printStatement($moduleUri, 'rdfs:label', '"'.ucfirst($name).'"');
            }
            foreach ($config as $prop => $val) {
                if (!is_array(($val))) {
                    $this->_printer->printStatement(
                        $moduleUri, 
                        $this->_printer->getPredicate($prop, ''), 
                        $this->_printer->getObject($prop, $val)
                    );
                } else {
                    foreach ($val as $subval) { //recursion is not needed (?)
                        $this->_printer->printStatement(
                            $moduleUri, 
                            $this->_printer->getPredicate($prop, ''), 
                            $this->_printer->getObject($prop, $subval)
                        );
                    }
                }
            }
            $this->_printer->resetLastSubject();
        }

        if (is_array($this->properties)) {
            foreach ($this->properties as $name => $value) {
                if (is_array($value)) {
                    if (self::is_numeric($value)) {
                       foreach ($value as $subval) {
                           $this->_printer->printStatement(
                               $this->_subject, 
                               $this->_printer->getPredicate($name, 'private'), 
                               $this->_printer->getObject($name, $subval)
                           );
                       } 
                    } else {
                        $this->printProperty($name, $value, 1, null);
                    }
                } else {
                    $this->_printer->printStatement(
                        $this->_subject, 
                        $this->_printer->getPredicate($name, 'private'), 
                        $this->_printer->getObject($name, $value)
                    );
                }
            }
        } 
    }

}

class Converter
{
    static function convert($iniPath, $extension)
    {
        ob_start();
        $privNS = "http://ns.ontowiki.net/Extensions/$extension/";
        //!!!!REMOVE THIS LINE AFTER YOU HAVE REVIEWED/FIXED THIS FILE!!!!
        echo <<<EOT
@prefix xsd: <http://www.w3.org/2001/XMLSchema#>.
@prefix doap: <http://usefulinc.com/ns/doap#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owconfig: <http://ns.ontowiki.net/SysOnt/ExtensionConfig/> .
@prefix extension: <http://ns.ontowiki.net/Extensions/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix event: <http://ns.ontowiki.net/SysOnt/Events/> .
@prefix : <$privNS> .


EOT;
        require_once __DIR__.'/../../libraries/Zend/Config.php';
        require_once __DIR__.'/../../libraries/Zend/Config/Ini.php';
        $config = new Zend_Config_Ini($iniPath, null, true);
        $config = $config->toArray();
        if(!isset($config['default'])){
            $config['default'] = array();
        }
        foreach ($config as $sectionname => $sectionconf) {
            if(!in_array($sectionname, array('default','events', 'private'))){
                $config['default'][$sectionname]  = $sectionconf;
                unset($config[$sectionname]);
            }
        }
        //var_dump($config); exit;

        $subject = ':this';
        $es = new ExtensionSerializer();
        $es->printStatement('<>', 'foaf:primaryTopic', $subject);
        $es->printStatement($subject, 'a', 'doap:Project');
        $es->printStatement($subject, 'doap:name', '"'.$extension.'"');
        $es->printStatement($subject, 'owconfig:privateNamespace', '<'.$privNS.'>');
        
        $mp = new NestedPropertyAndModuleHandler($es, $subject);

        foreach ($config as $sectionname => $sectionconf) {
            if ($sectionname == 'private') {
                $mp->properties = $sectionconf;
                continue;
            } 
            if (is_array($sectionconf)) {
                foreach ($sectionconf as $property => $value) {
                    if ($property == 'modules') {
                        $mp->modules = array_merge_recursive($mp->modules, $value);
                        continue;
                    } else if ($property == 'priority' || $property == 'contexts' 
                            || $property == 'caching' || $property == 'title') {
                        $mp->modules = array_merge_recursive(
                            $mp->modules, 
                            array('default'=>(array($property=>$value)))
                        );
                    } else if ($sectionname == 'default' && $property == 'helperEvents') {
                        $predicate = 'owconfig:helperEvent';
                        if(is_array($value)){
                            foreach ($value as $v){
                                $object = 'event:'.$v;
                                $es->printStatement($subject, $predicate, $object);
                            }
                        } else {
                            $object = 'event:'.$value;
                            $es->printStatement($subject, $predicate, $object);
                        }
                    } else if ($sectionname == 'events') {
                        $predicate = 'owconfig:pluginEvent';
                        if(is_array($value)){
                            foreach ($value as $v){
                                $object = 'event:'.$v;
                                $es->printStatement($subject, $predicate, $object);
                            }
                        } else {
                            $object = 'event:'.$value;
                            $es->printStatement($subject, $predicate, $object);
                        }
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
        
        $version = ':v1-0';
        $es->printStatement($subject, 'doap:release', $version);
        $es->printStatement($version, 'a', 'doap:Version');
        $es->printStatement($version, 'doap:revision', '"1.0"');
        
        //make sure the destructors are called
        $es = null;
        $mp = null;
        
        $res = ob_get_clean();

        //some wtf fixes :)
        $res = str_replace(";;", ";", $res);
        $res = str_replace("[;", "[", $res);
        $res = str_replace("; ;", ";", $res);
        $res = str_replace("[ ;", "[", $res);
        $res = preg_replace("/\\]\s*\.?\n\s*_:[0-9]*/", "];\n", $res);
        $res = preg_replace("/.\n\s*_:[0-9]*/", ";\n", $res);
        return $res;
    }
}
$path = realpath(__DIR__.'/../../libraries/');
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once realpath(__DIR__.'/../../libraries/Erfurt/Erfurt/Uri.php');

if ($argc > 4) {
    echo 'usage: extensions-ini2n3.sh [<extension-name>]'.PHP_EOL; exit(-1);
} else {
    if ($argc==2) {
        echo Converter::convert(__DIR__.'/../../extensions/'.$argv[1].'/default.ini', $argv[1]);
    } else if ($argc==4) {
        $in = $argv[1];
        $out = $argv[2];
        $name = $argv[3];
        $newContent = Converter::convert($in, $name);
        file_put_contents($out, $newContent);
    } else {
        $dir = realpath(__DIR__.'/../../extensions/');
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                $fullPath = realpath($dir.DIRECTORY_SEPARATOR.$file);
                if (
                        $file != "." && $file != ".." && $file != "themes" && 
                        $file != "translations" && is_dir($fullPath) && is_writable($fullPath)
                ) {
                    echo $file.PHP_EOL;
                    $origIni = realpath($fullPath.DIRECTORY_SEPARATOR.'default.ini');
                    if (file_exists($origIni) && is_readable($origIni)) {
                        $newFile = $fullPath."/doap.n3";
                        echo "write ".$newFile.PHP_EOL;
                        file_put_contents($newFile, Converter::convert($origIni, $file));
                    } else {
                        echo 'no default.ini in '.$fullPath.PHP_EOL;
                    }
                } else {
                    echo 'skipping non-extension dir '.$fullPath.PHP_EOL;
                }
            }
            closedir($handle);
        }
    }
}
