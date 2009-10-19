<? 
//include ('ajax.php');
require_once('Options.php');
echo "<xmp>";
//$arr = array (     'sembib:HumanAttribute' => 3   ,  'sembib:WaterArea' => 6  ,   'sembib:GeographicArea' => 1 ) ;
//sort($arr);
//var_dump($arr);
//die;
//Options::$pathToIni="config.ini";

$json = '[{\"s\":\"?city\",\"p\":\"leader_name\",\"o\":\"?leader\"}]';
$json = '[{\"s\":\"?city\",\"p\":\"db-ont:birthplace\",\"o\":\"?leader\"}]';
//$str = '[{\"s\":\"?city\",\"p\":\"fdsfjowl:disjointWith\",\"o\":\"?leader\"}]';
//$str = '{\"0\":{\"s\":\"aaaaaa\",\"p\":\"?dunno\",\"o\":\"?leader\",\"current\":\"s\"}}';
//$str= '{\"0\":{\"s\":\"?all\",\"p\":\"sembib:spouseOf\",\"o\":\"?leader\",\"current\":\"s\"}}';
$json = '{"0":{"s":"?what","p":"db-ont:releaseDate","o":"2007-09-25","otype":"typed-literal","lang":"","datatype":"http://www.w3.org/2001/XMLSchema#date"}}';
$json = '{\"0\":{\"s\":\"?what\",\"p\":\"db-ont:releaseDate\",\"o\":\"2004-03-08\",\"lang\":\"\"},\"1\":{\"s\":\"?what\",\"p\":\"?newPredicate\",\"o\":\"?newObject\",\"otype\":\"\",\"lang\":\"\",\"datatype\":\"\"}}';
$json = '{"0":{"s":"?what","p":"name","o":"?released","lang":""},"1":{"s":"?what","p":"?newPredicate","o":"California State University, Long Beach","otype":"literal","lang":"en","datatype":"","current":"p"}}';
$json = '{"0":{"s":"?what","p":"name","o":"?released","lang":""},"1":{"s":"?what","p":"?newPredicate","o":"California","lang":"","current":"o"}}';
//test($str);
echo $json."\n";
include("ajax.php");
//
echo getSPARQLQuery($json,  10)."\n\n";


echo getResults($json, 10)."\n\n";

//$json= '{\"0\":{\"s\":\"?what\",\"p\":\"db-ont:birthplace\",\"o\":\"e\",\"current\":\"o\"}}';
//echo getAutocompletionQuery($json,  10)."\n\n";

//include ('autocompletion.php');
//print_r(suggest($str,100));

?>
