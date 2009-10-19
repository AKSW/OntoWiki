#!/usr/bin/env php5
<?php
/**
 * OntoWiki command line client
 *
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://www.gnu.org/licenses/gpl.txt  GNU GENERAL PUBLIC LICENSE v2
 * @version    SVN: $Id: owcli.php 4322 2009-10-19 10:18:57Z sebastian.dietzold $
 * @link       http://ontowiki.net/Projects/OntoWiki/CommandLineInterface
 */
class OntowikiCommandLineInterface {
    
    const NAME = 'The OntoWiki CLI';
    const VERSION = '0.3pre $Revision: 1502 $';

    /* Required PEAR Packages */
    protected $pearPackages = array(
        'Console/Getargs.php',
        'Console/Table.php',
        'Config.php'
    );

    /* Command Line Parameter Config */
    protected $argConfig = array();
    /* Command Line Parameter Getarg Object */
    protected $args;
    /* Parsed Config Array */
    protected $config;
    /* the parsed config of the active wiki */
    protected $wikiConfig;

    public function __construct() {
        // load pear packages
        $this->initPackages();
        // check command line parameters
        $this->initCommandLineArguments();
        // check and initialize config file
        $this->initConfig();

        $this->echoDebug('Everything ok, start to execute commands:');
        foreach ((array) $this->args->getValue('execute') as $command) {
            $result = $this->executeJsonRpc($command);
            $this->renderResult ($result);
        }
    }


    /*
     * Renders a rpc result
     *
     * @param string $result  the result from an executeJsonRpc call
     */
    protected function renderResult($response) {
        if ($this->args->isDefined('raw')) {
            // raw output is easy ...
            echo $response . PHP_EOL;
        } else {
            // try to decode and look for the content to decide how to echo
            $decodedResult = json_decode($response, true);
            if (!$decodedResult) {
                // if decoding fails, something went wrong
                $this->echoError('Something went wrong, response was not json encoded (turn debug on to see more)');
                $this->echoDebug($response);
            } else {
                if ($decodedResult['error']) {
                    // if we have an rpc error, output is easy too
                    $error = $decodedResult['error'];
                    $this->echoError('Error '.$error['code'].': '.$error['message']);
                } elseif (isset($decodedResult['result'])) {
                    #var_dump($decodedResult['result']); die();
                    // different rendering for different results
                    $result = $decodedResult['result'];
                    if (is_array($result)) {
                        if (count($result) == 0) {
                            // e.g. on sparql queries without without result
                            echo 'Empty result' . PHP_EOL;
                        } elseif (!is_array($result[0])) {
                            // simply output for one-dimensional arrays
                            foreach ($result as $row) {
                                echo $row . PHP_EOL;
                            }
                        } else {
                            // table output for multidimensional arrays
                            echo $this->renderTable($result);
                        }
                    } elseif ( (is_numeric($result)) || (is_string($result)) ) {
                        // all simple result type are printed with echo
                        echo $result . PHP_EOL;
                    } else  {
                        print_r($result) .PHP_EOL;
                    }
                } else {
                    $this->echoError('Something went wrong, neither result nor error in response.');
                }
            }
        }
    }

    protected function renderTable ($result) {
        $table = new Console_Table;

        #var_dump($result); die();
        $firstrow = true;
        foreach ($result as $row) {

            if ($firstrow == true) {
                $i=0;
                foreach ($row as $key => $var) {
                    $headrow[$i] = $key;
                    $i++;
                }
                $table->setHeaders ($headrow);
                $firstrow = false;
            }

            // prepare content row array
            $i=0;
            foreach ($row as $var) {
                $LabelRow[$i] = $var;
                $i++;
            }
            $table->addRow($LabelRow);
        }

        // output the table
        return $table->getTable();
   }

    /*
     * Execute a specific remote procedure and return the response string
     *
     * @param string $command  the remote procedure
     */
    protected function executeJsonRpc ($command) {
        $this->echoDebug('starting jsonrpc: ' . $command);

        // create a new cURL resource
        $rpc = curl_init();
        curl_setopt ($rpc, CURLOPT_USERAGENT, self::NAME . ' ' . self::VERSION);
        curl_setopt ($rpc, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($rpc, CURLOPT_CONNECTTIMEOUT, 30);

        // checks and matches the command
        $pattern = '/^([a-z]+)\:([a-zA-Z]+)(\:(.*))?$/';
        preg_match($pattern, $command, $matches);
        if (count($matches) == 0 ) {
            $this->echoError('The command "'.$command.'" is not valid by regular expression.');
            return;
        } else {
            $zendAction = $matches[1];
            $rpcMethod = $matches[2];
            $rpcParameter = $matches[4];
        }

        // define jsonrpc server URL
        $serveruri = $this->wikiConfig['baseuri'] . '/jsonrpc/'.$zendAction;
        curl_setopt ($rpc, CURLOPT_URL, $serveruri);

        // define the post data
        #$postdata = '{"method": "getAvailableModels", "params": {"uri": "yourmodeluri"}, "id": 33}';
        $postdata['method'] = $rpcMethod;

        if ($rpcParameter) {
            $postdata['params']['p1'] = $rpcParameter;
        } else {
            $postdata['params']['modelIri'] = $this->args->getValue('model');
        }
        $postdata['id'] = 1;
        $postdata = json_encode($postdata);
        $this->echoDebug('postdata: ' . $postdata);
        curl_setopt ($rpc, CURLOPT_POST, true);
        curl_setopt ($rpc, CURLOPT_POSTFIELDS, $postdata);

        // add authentification header if there are auth credentials configured
        if ( $this->wikiConfig['user'] && $this->wikiConfig['password'] ) {
            $headers = array(
                "Authorization: Basic " . 
                    base64_encode($this->wikiConfig['user'].':'.$this->wikiConfig['password'])
            );
            curl_setopt ($rpc, CURLOPT_HTTPHEADER, $headers);
            curl_setopt ($rpc, CURLOPT_HTTPAUTH, CURLAUTH_ANY);            
        }

        // catch URL and work on response
        $response = curl_exec($rpc);
        curl_close($rpc);
        return $response;
    }

    /**
     * Write STDERR-String
     */
    protected function echoError ($string) {
        fwrite(STDERR, $string ."\n");
    }

    /**
     * Write STDERR-String if Debug-Mode on
     */
    protected function echoDebug ($string) {
	if ($this->args->isDefined('debug')) {
            fwrite(STDERR, $string . "\n");
        }
    }

    /*
     * Load required Packages
     */
    protected function initPackages() {
	foreach ($this->pearPackages as $package) {
            if (!require_once($package) ) {
                $this->echoError("PEAR package $package needed!");
                die();
            }
        }
    }

    /*
     * Generate command line parameter array for Console_Getargs
     */
    protected function initCommandLineArguments() {

        // Some default parameter values can be overwritten by variables
        $defaultModel = getenv('OWMODEL') ? getenv('OWMODEL') : 'http://localhost/OntoWiki/Config/';
        $defaultWiki = getenv('OWWIKI') ? getenv('OWWIKI') : 'default';
        $defaultConfig = getenv('OWCONFIG') ? getenv("OWCONFIG") : getenv('HOME').'/.owcli';

        $this->argConfig = array(
            'execute' => array(
                'short' => 'e',
                'min' => 1,
                'max' => -1,
                'desc' => 'Execute one or more commands on a given wiki/graph'
            ),

            'wiki' => array(
                'short' => 'w',
                'max' => 1,
                'default' => $defaultWiki,
                'desc' => 'Set OntoWiki database which should be used'
            ),

            'model' => array(
                'short' => 'm',
                'max' => 1,
                'default' => $defaultModel,
                'desc' => 'Set model which should be used'
            ),

            'input' => array(
                'short' => 'i',
                'min' => 1,
                'max' => -1,
                'default' => "-",
                'desc' => 'input model file (- for STDIN)'
            ),

            'output' => array(
                'short' => 'o',
                'min' => 1,
                'max' => 1,
                'default' => "-",
                'desc' => 'output model file (- for STDOUT)'
            ),

            'config' => array(
                'short' => 'c',
                'max' => 1,
                'default' => $defaultConfig,
                'desc' => 'Set a config file'
            ),

            'debug' => array(
                'short' => 'd',
                'max' => 0,
                'desc' => 'Output some debug infos'
            ),

            'quiet' => array(
                'short' => 'q',
                'max' => 0,
                'desc' => 'Do not output info messages'
            ),

            'raw' => array(
                'short' => 'r',
                'max' => 0,
                'desc' => 'outputs the result in raw json instead of nice tables etc.'
            ),

            'help' => array(
                'short' => 'h',
                'max' => 0,
                'desc' => 'Show this screen'
            ),
        );

	$header = self::NAME . ' ' . self::VERSION . PHP_EOL .
		'Usage: '.basename($_SERVER['SCRIPT_NAME']).' [options]' . PHP_EOL . PHP_EOL;
	$footer = PHP_EOL . 'Note: Some commands are limited to the php.ini value memory_limit ...';

	$this->args =& Console_Getargs::factory($this->argConfig);

	if (PEAR::isError($this->args)) {
            if ($this->args->getCode() === CONSOLE_GETARGS_ERROR_USER) {
                $this->echoError ($this->args->getMessage());
                $this->echoError (PHP_EOL . 'Try "'.basename($_SERVER['SCRIPT_NAME']).' --help" for more information');
            }
            elseif ($this->args->getCode() === CONSOLE_GETARGS_HELP) {
                $this->echoError (Console_Getargs::getHelp($this->argConfig, $header, $footer));
            }
            die();
	} elseif (count($this->args->args) == 0) {
            $this->echoError (self::NAME ." ". self::VERSION);
            $this->echoError ('Try "'.basename($_SERVER['SCRIPT_NAME']).' --help" for more information');
            exit();
	}
    }

    /**
     * Load and check config file
     */
    protected function initConfig() {
        $file = $this->args->getValue('config');
	$config = @parse_ini_file($file, TRUE);

	if (!isset($config)) {
            $this->echoError ('Can\'t open config file $file');
            die();
	}

	$wiki = $this->args->getValue('wiki');
	if (!isset($config[$wiki])) {
            $this->echoError ('Wiki instance '.$wiki.' not configured in configfile '.$file);
            die();
	} elseif ( !isset($config[$wiki]['baseuri']) ) {
            $this->echoError ('Wiki instance '.$wiki.' has no baseuri in configfile '.$file);
            die();
        }

        $this->wikiConfig = $config[$wiki];

	$this->config = $config;

        #$this->wiki = $this->config[]
	$this->echoDebug ('Config file loaded and ok');

    }

}

// start the programm
$owcli = new OntowikiCommandLineInterface();
