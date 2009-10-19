<?php

class Dbpedia{

var $pdo;

function __construct(){
		//$this->pdo = $this->init();
	}
	
//SELECT *, count(*) as count FROM triples WHERE s LIKE 'Soc%' GROUP BY s ORDER BY count DESC
//SELECT *, count (*) FROM triples WHERE s LIKE 'Soc%' LIMI 100
//SELECT *, count(*) as count FROM triples WHERE s LIKE 'Socc%' GROUP BY s LIMIT 100
//SELECT s, count(s) as count FROM triples WHERE s LIKE 'Socc%' GROUP BY s ORDER BY count DESC LIMIT 100
//geht mit mehr als 2 chars
//SELECT s, count(s) as count FROM triples WHERE s LIKE 'Socc%' GROUP BY s ORDER BY count DESC LIMIT 100
//SELECT s, count(s) as count FROM triples WHERE s LIKE 'Socc%' AND p ='name' GROUP BY s ORDER BY count DESC LIMIT 100
//SELECT p, count(p) as c FROM triples GROUP BY p ORDER BY c DESC LIMIT 100

function getListWithCount ($patterns){
	$this->init();
	foreach ($patterns as $pattern){
		if(!isset($pattern['current'] )){
				continue;
			}
		$searchrow = $pattern['current'];
		$searchValue;
		$fixedkey;
		$fixedvalue;
		foreach($pattern as $key=>$value){
			if($key == $searchrow){
				$searchValue = $value;
				//do nothing
			}else if($this->startsWith($value, '?')){
				//do nothing
				}
			else if($key == 's' ||$key == 'p' ||$key == 'o' ){
				$fixedkey = $key;
				$fixedvalue = $value;
			}
		}
	}//end foreach
		$select = ($searchrow == 'o')?'o, otype, datatype, lang ':$searchrow;
		if(isset($fixedkey)){
			$tmp = ' '.$fixedkey.' = \''.$fixedvalue.'\' ';
			if(strlen($searchValue)>0){
				$where = 'WHERE  '.$tmp.' AND '.$searchrow.' LIKE \''.$searchValue.'%\' ';
				}else{
					$where = 'WHERE  '.$tmp;
				}
		}else{
			echo "not implemented\n";
			die;
			}
		
		
		$sql = 
		'SELECT '.$select. ' , count('.$searchrow.') as c '."\n". 
		//'SELECT DISTINCT '.$select. ' '."\n". 
		'FROM triples '.
		$where.
		//optional fixed
		'GROUP BY '.$searchrow.' '."\n";
		'LIMIT 10'."\n";
		//'GROUP BY '.$searchrow.' ORDER BY c DESC LIMIT 100'."\n";
		//echo $sql;die;
		$result = $this->pdo->query($sql);
		//var_dump($result);
		if($result == false){
			print_r($this->pdo->errorInfo());
			die;
			//echo $this->pdo->getLastError();
			}
		 $list = array();	
		 foreach ($result as $row) {
		 	$tmp = array();
		 	//print_r($row);
		 	$tmp['value'] = $row[$searchrow];
		 	$tmp['count'] = 1;//@$row['c'];
			$tmp['type'] =  @$row['otype'];
			$tmp['lang'] = @$row['lang'];
			$tmp['datatype'] =@$row['datatype'];
			//print_r($tmp);
			$list[] = $tmp;
		  }
		return $list;		
}//end function
	
	
function checkIfApplies($patterns){
		if(count($patterns)==1){
			return true;
			}
		
		$otherVars = array();
		$currentVars = array();
		foreach ($patterns as $pattern){
			$current = false;
			//print_r($pattern);
			foreach ($pattern as $key=>$value){
				
				if(isset($pattern['current'] )){
					$current = true;	
				}else{
					$current = false;	
				}
				if ($this->startsWith($value, '?')){
					if($current){
						$currentVars[]=$value;
						}
					else{
						$otherVars[]=$value;
						}
					}
				/*else if($current && $key==$pattern['current']){
						
					}*/
			}
			
			
				//print_r($patterns);
		}
		//print_r($currentVars);
		//print_r($otherVars);
		if(count(array_intersect($currentVars, $otherVars))==0){
				
				return true;
				}	
	
		return false;	
				
	}
	
function startsWith($Haystack, $Needle){
  	  	// Recommended version, using strpos
 	   		return strpos($Haystack, $Needle) === 0;
		}


	function init(){

		/*** mysql hostname ***/
		$hostname = 'exp1.aksw.org';
		//$hostname = '139.18.2.56';

		/*** mysql username ***/
		$username = 'root';

		/*** mysql password ***/
		$password = 'softwiki';
		$pdo;

		$dbname = "querybuilder";

		try {
			$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
			/*** echo a messdbhage saying we have connected ***/
			//echo 'Connected to database'."\n";
			}
		catch(PDOException $e)
			{
			echo $e->getMessage();
			}
			
			
		$this->pdo = $pdo;
	}


}



