<?php

class Logger{
   
   	static $silent = false;
   	static $knownLog = array();
   	static $time;
   	static $defaultAppend = "\n";
   	
   	public static function setTime(){
   			self::$time = microtime(true);
   		}
   	
   	public static function getTimeElapsed($reset = true){
   			$ret =  (microtime(true) - self::$time);
   			if($reset){
   				self::$time = microtime(true);
   			}
   			return $ret;
   			
   		}
  
	public static function addDate($str) {
		return "[".date('Y-m-d-h-m-s', time())."] ".$str; 
		
	}
	
	public static function arrayToFile($file, $array, $overwrite = false){
			
    		ob_start();
    		 print_r($array);
    		$str= ob_get_clean();
    		if(!self::$silent)
    			self::toFile($file, $str."\n\n",$overwrite );
		}
		
	public static function logInFile($file, $str, $overwrite = false){
			if(!self::$silent)
				self::toFile($file,Logger::addDate($str).self::$defaultAppend,$overwrite );
		}
		
	public static function toFile($file, $str, $overwrite = false){
			
			
			if(in_array($file, self::$knownLog)){
					$mode = 'a';
				}
			else{
					$mode = 'w';
					self::$knownLog[] = $file;
				}
			
			if($overwrite)$mode = 'w';
			
			$fp = fopen($file,$mode);
    		fwrite($fp, $str);
			fclose($fp);
		}
		
		public static function appendToFile($file, $str){
			
			
			if(!self::$silent){
				$mode = 'a';
				$fp = fopen($file,$mode);
			
    			fwrite($fp, $str);
				fclose($fp);
			}
		}
	
	
		
	
	
}

?>
