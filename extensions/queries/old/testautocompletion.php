<? 
require_once('Options.php');
Options::$pathToIni="config.ini";
//include ('ajax.php');
echo "<xmp>";
//$arr = array (     'sembib:HumanAttribute' => 3   ,  'sembib:WaterArea' => 6  ,   'sembib:GeographicArea' => 1 ) ;
//sort($arr);
//var_dump($arr);
//die;

$json = '[{\"s\":\"?city\",\"p\":\"leader_name\",\"o\":\"?leader\"}]';
$json = '[{\"s\":\"?city\",\"p\":\"owl:disjointWith\",\"o\":\"?leader\"}]';
$json = '[{\"s\":\"?city\",\"p\":\"fdsfjowl:disjointWith\",\"o\":\"?leader\"}]';
//$str = '{\"0\":{\"s\":\"aaaaaa\",\"p\":\"?dunno\",\"o\":\"?leader\",\"current\":\"s\"}}';
$json= '{\"0\":{\"s\":\"Abra\",\"p\":\"sembib:spouseOf\",\"o\":\"?leader\",\"current\":\"s\"}}';
$json= '{\"0\":{\"s\":\"tommy_T\",\"p\":\"db-ont:birthplace\",\"o\":\"db:England\",\"current\":\"s\"}}';
$json= '{\"0\":{\"s\":\"?what\",\"p\":\"db-ont:releaseDate\",\"o\":\"200\",\"current\":\"o\"}}';
$json = '{"0":{"s":"?what","p":"db-ont:releaseDate","o":"2007-09-25","otype":"typed-literal","lang":"","datatype":"http://www.w3.org/2001/XMLSchema#date"},"1":{"s":"?what","p":"releas","o":"?newObject","otype":"typed-literal","lang":"","datatype":"http://www.w3.org/2001/XMLSchema#date","current":"p"}}';
$json = '{"0":{"s":"?what","p":"name","o":"?released","lang":""},"1":{"s":"?what2","p":"name","o":"Cal","lang":"","current":"o"}}';
$json = '{"0":{"s":"?actors","p":"tes","o":"db-ont:Actor","lang":"","current":"p"}}';

//test($str);

$_REQUEST['q'] = 'foaf:nam';
$_REQUEST['json'] = $json;
$_REQUEST['limit'] = 10;

echo $json."\n";
global $debug ;
$debug = true;

include ('autocompletion.php');
//print_r(suggest($str,100));

?>
